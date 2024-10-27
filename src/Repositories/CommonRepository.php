<?php

namespace zaheensayyed\FilamentCms\Repositories;

use zaheensayyed\FilamentCms\Models\Page;
use zaheensayyed\FilamentCms\Models\Gallery;
use zaheensayyed\FilamentCms\Models\NavigationItem;

class CommonRepository
{
    public static function mutateDataForCreatedBy(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }

    public static function mutateDataForUpdatedBy(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }

    public static function navigationTypeSelectOptions(string $type): array
    {
        $options = [];
        switch ($type) {
            case NavigationItem::TYPE_PAGE:
                $options = Page::pluck('title', 'id')->toArray();

                break;

            case NavigationItem::TYPE_GALLERY:
                $options = Gallery::pluck('name', 'id')->toArray();

                break;

            default:
                // code...
                break;
        }

        return $options;
    }
}
