<?php

namespace zaheensayyed\FilamentCms\Resources\NavigationResource\Pages;

use zaheensayyed\FilamentCms\Resources\NavigationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use zaheensayyed\FilamentCms\Traits\CommonResourceTrait;

class EditNavigation extends EditRecord
{
    use CommonResourceTrait;

    protected static string $resource = NavigationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
