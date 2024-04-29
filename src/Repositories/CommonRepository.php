<?php

namespace zaheensayyed\FilamentCms\Repositories;

use zaheensayyed\FilamentCms\Models\NavigationItem;
use zaheensayyed\FilamentCms\Models\Page;

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

            default:
                // code...
                break;
        }

        return $options;
    }
}
