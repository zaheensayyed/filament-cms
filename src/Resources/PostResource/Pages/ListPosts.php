<?php

namespace zaheensayyed\FilamentCms\Resources\PostResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;
use zaheensayyed\FilamentCms\Models\Post;
use zaheensayyed\FilamentCms\Resources\PostResource;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Every draft and scheduled post goes live now; hidden when there are none.
            Actions\Action::make('publishAll')
                ->label('Publish all')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Publish all posts')
                ->modalDescription(function () {
                    $count = Post::unpublished()->count();

                    return $count . ' ' . Str::plural('post', $count) . ' (every draft and scheduled post) will go live now.';
                })
                ->modalSubmitActionLabel('Publish all')
                ->visible(fn () => PostResource::canPublish() && Post::unpublished()->exists())
                ->action(fn () => PostResource::publish(Post::unpublished()->get())),
            Actions\CreateAction::make(),
        ];
    }
}
