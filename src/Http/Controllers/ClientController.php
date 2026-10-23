<?php

namespace zaheensayyed\FilamentCms\Http\Controllers;

use Illuminate\Contracts\View\View;
use zaheensayyed\FilamentCms\FilamentCms;

class ClientController
{
    /**
     * Published clients as cards, a page at a time; there are no pages per client.
     */
    public function __invoke(): View
    {
        return view(config('filament-cms.clients.views.index'), ['clients' => FilamentCms::paginateClients()]);
    }
}
