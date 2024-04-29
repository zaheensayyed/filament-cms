<?php

namespace zaheensayyed\FilamentCms\Resources\PageResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use zaheensayyed\FilamentCms\Resources\PageResource;
use zaheensayyed\FilamentCms\Traits\CommonResourceTrait;

class EditPage extends EditRecord
{
    use CommonResourceTrait;

    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
