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

    /*
    |--------------------------------------------------------------------------
    | Blog
    |--------------------------------------------------------------------------
    |
    | Posts are written in the panel (Blog Posts). Opt-in frontend routes:
    | GET /{prefix} lists published posts (route "filament-cms.blog.index") and
    | GET /{prefix}/{slug} shows one (route "filament-cms.blog.show"). Drafts
    | and scheduled posts are 404 except for users who can edit them (preview).
    |
    */

    'blog' => [
        'routes' => env('FILAMENT_CMS_BLOG_ROUTES', false),

        'prefix' => 'blog',

        'middleware' => ['web'],

        'per_page' => 9,

        // Views receive $posts (paginator of Post) or $post (Post).
        'views' => [
            'index' => 'filament-cms::blog.index',
            'show' => 'filament-cms::blog.show',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Clients
    |--------------------------------------------------------------------------
    |
    | Clients are added in the panel (Clients): title, description and images,
    | and a Featured switch for the home page (FilamentCms::featuredClients()).
    | Opt-in frontend route: GET /{prefix} shows every published client as a
    | card on one page (route "filament-cms.clients.index"). There are no pages
    | per client; link to the page with a custom URL menu item (/{prefix}).
    |
    */

    'clients' => [
        'routes' => env('FILAMENT_CMS_CLIENT_ROUTES', false),

        'prefix' => 'clients',

        'middleware' => ['web'],

        // The view receives $clients (Collection of Client).
        'views' => [
            'index' => 'filament-cms::clients.index',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Navigations
    |--------------------------------------------------------------------------
    |
    | The only navigations the panel manages (key => name). They are created
    | automatically when the Navigations list is opened; creating and deleting
    | navigations in the panel is turned off. Render them with
    | FilamentCms::getMenu('main-menu').
    |
    */

    'navigations' => [
        'main-menu' => 'Main Menu',
        'footer-menu' => 'Footer Menu',
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact form
    |--------------------------------------------------------------------------
    |
    | POST endpoint used by <x-filament-cms::contact-form /> (route name
    | "filament-cms.contact.submit"). Recipients, subject prefix, success message
    | and the on/off switch live in the panel: Settings → Contact Form.
    | Mail goes through the app's default mailer (config/mail.php).
    |
    */

    'contact_form' => [
        'enabled' => true,

        'path' => 'filament-cms/contact',

        'middleware' => ['web'],

        // Submissions allowed per minute per IP address.
        'rate_limit' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Roles & permissions (Filament Shield)
    |--------------------------------------------------------------------------
    |
    | Roles seeded by `php artisan filament-cms:install` / `filament-cms:roles`.
    | The admin role is Shield's super-admin role: it passes every permission
    | check, including resources added later.
    |
    */

    'shield' => [
        'admin_role' => 'admin',
        'content_manager_role' => 'content_manager',
    ],

];
