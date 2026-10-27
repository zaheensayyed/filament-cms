<?php

namespace zaheensayyed\FilamentCms\Resources\ClientResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use zaheensayyed\FilamentCms\Resources\ClientResource;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
