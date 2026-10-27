<?php

namespace zaheensayyed\FilamentCms\Resources\PostResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use zaheensayyed\FilamentCms\Models\Post;
use zaheensayyed\FilamentCms\Resources\PostResource;
use zaheensayyed\FilamentCms\Traits\CommonResourceTrait;

class EditPost extends EditRecord
{
    use CommonResourceTrait;

    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Drafts open as a preview for users who can edit them.
            Actions\Action::make('view')
                ->label('View post')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (Post $record) => $record->url, shouldOpenInNewTab: true),
            Actions\DeleteAction::make(),
        ];
    }
}
