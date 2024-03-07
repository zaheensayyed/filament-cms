<?php

namespace zaheensayyed\FilamentCms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \zaheensayyed\FilamentCms\FilamentCms
 */
class FilamentCms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \zaheensayyed\FilamentCms\FilamentCms::class;
    }
}
