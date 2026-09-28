<?php

use Illuminate\Support\Facades\Route;
use zaheensayyed\FilamentCms\FilamentCms;
use zaheensayyed\FilamentCms\Http\Controllers\CmsPageController;

Route::middleware(config('filament-cms.routes.middleware', ['web']))
    ->prefix(config('filament-cms.routes.prefix', ''))
    ->group(function () {
        Route::get('{slug}', CmsPageController::class)
            ->where('slug', '.*')
            ->name(FilamentCms::ROUTE_NAME)
            ->fallback();
    });
