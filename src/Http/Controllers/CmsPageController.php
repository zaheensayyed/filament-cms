<?php

namespace zaheensayyed\FilamentCms\Http\Controllers;

use Illuminate\Contracts\View\View;
use zaheensayyed\FilamentCms\FilamentCms;
use zaheensayyed\FilamentCms\Models\Gallery;

class CmsPageController
{
    public function __invoke(string $slug): View
    {
        $content = FilamentCms::resolveSlug($slug);

        abort_if($content === null, 404);

        if ($content instanceof Gallery) {
            return view(config('filament-cms.routes.views.gallery'), ['gallery' => $content]);
        }

        return view(config('filament-cms.routes.views.page'), ['page' => $content]);
    }
}
