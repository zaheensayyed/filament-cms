<?php

// config for zaheensayyed/FilamentCms
return [

    /*
    |--------------------------------------------------------------------------
    | Frontend routes
    |--------------------------------------------------------------------------
    |
    | Opt-in catch-all route that renders CMS pages and galleries by slug, so the
    | consumer app no longer registers one route per menu item. It is registered
    | as a fallback route (matched after all of the app's own routes) and uses a
    | controller, so `php artisan route:cache` works.
    |
    */

    'routes' => [
        'enabled' => env('FILAMENT_CMS_ROUTES', false),

        // e.g. "pages" to serve CMS content at /pages/{slug} instead of /{slug}.
        'prefix' => '',

        'middleware' => ['web'],

        // Views receive $page (Page) or $gallery (Gallery, images loaded).
        'views' => [
            'page' => 'filament-cms::page',
            'gallery' => 'filament-cms::gallery',
        ],
    ],

];
