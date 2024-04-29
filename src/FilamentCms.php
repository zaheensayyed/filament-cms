<?php

namespace zaheensayyed\FilamentCms;

use zaheensayyed\FilamentCms\Models\Navigation;

class FilamentCms
{
    public static function getMenu($menu_name)
    {
        $navigation = Navigation::where('name', $menu_name)->first();
        if ($navigation) {
            return $navigation->items;
        }

        return [];
    }
}
