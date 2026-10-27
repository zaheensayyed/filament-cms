<?php

use Illuminate\Support\Facades\Route;
use zaheensayyed\FilamentCms\FilamentCms;
use zaheensayyed\FilamentCms\Http\Controllers\BlogController;

Route::middleware(config('filament-cms.blog.middleware', ['web']))
    ->prefix(config('filament-cms.blog.prefix', 'blog'))
    ->group(function () {
        Route::get('/', [BlogController::class, 'index'])->name(FilamentCms::BLOG_INDEX_ROUTE);
        Route::get('{slug}', [BlogController::class, 'show'])
            ->where('slug', '[^/]+')
            ->name(FilamentCms::BLOG_SHOW_ROUTE);
    });
