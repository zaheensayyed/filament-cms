<?php

use Illuminate\Support\Facades\Route;
use zaheensayyed\FilamentCms\Http\Controllers\ContactFormController;

Route::middleware([...config('filament-cms.contact_form.middleware', ['web']), 'throttle:filament-cms-contact'])
    ->post(config('filament-cms.contact_form.path', 'filament-cms/contact'), ContactFormController::class)
    ->name('filament-cms.contact.submit');
