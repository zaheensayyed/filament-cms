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
