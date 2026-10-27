<?php

use Illuminate\Support\Facades\Route;
use zaheensayyed\FilamentCms\FilamentCms;
use zaheensayyed\FilamentCms\Http\Controllers\ClientController;

Route::middleware(config('filament-cms.clients.middleware', ['web']))
    ->prefix(config('filament-cms.clients.prefix', 'clients'))
    ->group(function () {
        Route::get('/', ClientController::class)->name(FilamentCms::CLIENTS_INDEX_ROUTE);
    });
