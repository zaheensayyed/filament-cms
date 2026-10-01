# SEO

Put the component inside `<head>`:

```blade
{{-- CMS page --}}
<x-filament-cms::seo :page="$page" />

{{-- Any other route: emits the site-wide defaults --}}
<x-filament-cms::seo />
```

It renders the whole tag block: `<title>`, meta description, canonical link, robots,
Open Graph (`og:*`), Twitter card (`twitter:*`) and the page's JSON-LD, all HTML-escaped.
Don't add your own `<title>` next to it.

## Where each value comes from

Every value is resolved **page field → SEO Defaults setting → built-in fallback**:

| Tag | Page field | Then | Finally |
| --- | --- | --- | --- |
| `<title>` | `meta_title` (used as-is) | title pattern applied to the page title | site name |
| `description` | `meta_description` | `seo.default_description` | omitted |
| `canonical`, `og:url` | `canonical_url` | the page's own URL | current URL |
| `robots` | `robots` | `seo.default_robots` | `index,follow` |
| `og:title`, `twitter:title` | `og_title` | `meta_title`, then the page title | site name |
| `og:description`, `twitter:description` | `og_description` | the description above | omitted |
| `og:image`, `twitter:image` | `og_image` | `seo.default_og_image` | omitted |
| `og:site_name` | | `seo.site_name` | `config('app.name')` |
| `og:type` | | `article` for pages | `website` without a page |
| `twitter:card` | | | `summary_large_image` |
| JSON-LD | `structured_data` | | omitted |

The **title pattern** (`seo.title_pattern`, default `{title} | {site_name}`) is only applied
when a page has no meta title of its own.

## Site-wide defaults

**Settings → SEO Defaults**: site name, title pattern, default description, default share
image (recommended 1200×630) and default robots. Every page without its own values uses
these, so a page with no SEO fields still renders a complete tag block.

## Page URLs in canonicals

The canonical URL of a page defaults to its catch-all URL (`FilamentCms::url($page->slug)`).
If pages live somewhere else in your app, tell the package:

```php
use zaheensayyed\FilamentCms\Models\Page;
use zaheensayyed\FilamentCms\Seo\SeoMeta;

// AppServiceProvider::boot()
SeoMeta::resolvePageUrlUsing(fn (Page $page) => route('pages.show', $page->slug));
```

## Using the values yourself

The component is a thin view over `SeoMeta`, which you can use directly:

```php
$seo = new \zaheensayyed\FilamentCms\Seo\SeoMeta($page);

$seo->title();       // "About Us | KBI"
$seo->description();
$seo->ogImage();     // absolute URL or null
```

> **Performance notes:** the component runs **no queries** of its own: it reads the page you
> pass and the settings, which are cached (1 query per request at most, 0 when cached).
