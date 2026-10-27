<?php

namespace zaheensayyed\FilamentCms\Resources\ClientResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use zaheensayyed\FilamentCms\Models\Client;
use zaheensayyed\FilamentCms\Resources\ClientResource;
use zaheensayyed\FilamentCms\Traits\CommonResourceTrait;

class EditClient extends EditRecord
{
    use CommonResourceTrait;

    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // The clients page, where the client is one of the cards.
            Actions\Action::make('view')
                ->label('View client')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->visible(fn (Client $record) => $record->is_published)
                ->url(fn (Client $record) => $record->url, shouldOpenInNewTab: true),
            Actions\DeleteAction::make(),
        ];
    }
}
