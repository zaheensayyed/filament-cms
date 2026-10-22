{{-- Pass a CMS page as :page or a blog post as :post (or :page). --}}
@props(['page' => null, 'post' => null])

@php
    $seo = new \zaheensayyed\FilamentCms\Seo\SeoMeta($post ?? $page);
    $publishedTime = $seo->publishedTime();
    $title = $seo->title();
    $description = $seo->description();
    $canonical = $seo->canonical();
    $ogTitle = $seo->ogTitle();
    $ogDescription = $seo->ogDescription();
    $ogImage = $seo->ogImage();
    $structuredData = $seo->structuredData();
@endphp

<title>{{ $title }}</title>
@if ($description)
<meta name="description" content="{{ $description }}">
@endif
<link rel="canonical" href="{{ $canonical }}">
<meta name="robots" content="{{ $seo->robots() }}">

<meta property="og:type" content="{{ $seo->ogType() }}">
<meta property="og:site_name" content="{{ $seo->siteName() }}">
<meta property="og:title" content="{{ $ogTitle }}">
@if ($ogDescription)
<meta property="og:description" content="{{ $ogDescription }}">
@endif
<meta property="og:url" content="{{ $canonical }}">
@if ($ogImage)
<meta property="og:image" content="{{ $ogImage }}">
@endif
@if ($publishedTime)
<meta property="article:published_time" content="{{ $publishedTime }}">
@endif

<meta name="twitter:card" content="{{ $seo->twitterCard() }}">
<meta name="twitter:title" content="{{ $ogTitle }}">
@if ($ogDescription)
<meta name="twitter:description" content="{{ $ogDescription }}">
@endif
@if ($ogImage)
<meta name="twitter:image" content="{{ $ogImage }}">
@endif
@if ($structuredData)
{{-- Already re-encoded with JSON_HEX_TAG etc. in SeoMeta, so it cannot break out of the script tag. --}}
<script type="application/ld+json">{!! $structuredData !!}</script>
@endif
