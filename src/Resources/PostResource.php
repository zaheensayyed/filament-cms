<?php

namespace zaheensayyed\FilamentCms\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use zaheensayyed\FilamentCms\Models\Post;
use zaheensayyed\FilamentCms\Resources\PostResource\Pages;
use zaheensayyed\FilamentCms\Seo\SeoFields;
use zaheensayyed\FilamentCms\Shield\CmsPermissions;

class PostResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Post::class;

    protected static ?string $modelLabel = 'blog post';

    protected static ?string $navigationLabel = 'Blog Posts';

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getPermissionPrefixes(): array
    {
        return CmsPermissions::RESOURCE_PREFIXES;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        TextInput::make('title')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(debounce: 1000)
                                            ->afterStateUpdated(function (Set $set, ?string $state, string $operation) {
                                                // A saved post keeps its URL unless the slug is changed by hand.
                                                if ($operation === 'create') {
                                                    $set('slug', Str::slug((string) $state));
                                                }
                                            }),
                                        TextInput::make('slug')
                                            ->required()
                                            ->maxLength(255)
                                            ->alphaDash()
                                            ->unique(ignoreRecord: true)
                                            ->helperText('Part of the post URL: /blog/{slug}.'),
                                    ])
                                    ->columns(2),
                                Textarea::make('excerpt')
                                    ->helperText('Short summary for the blog list and the meta description. Empty uses the start of the body.')
                                    ->rows(3)
                                    ->maxLength(500),
                                RichEditor::make('body')
                                    ->fileAttachmentsDirectory('blog'),
                            ])
                            ->columnSpan(['lg' => 2]),
                        Section::make('Publishing')
                            ->schema([
                                Select::make('status')
                                    ->options(Post::STATUSES)
                                    ->default(Post::STATUS_DRAFT)
                                    ->required()
                                    ->native(false),
                                DateTimePicker::make('published_at')
                                    ->label('Publish date')
                                    ->helperText('Empty publishes now. A future date schedules the post.'),
                                FileUpload::make('cover_image')
                                    ->label('Cover image')
                                    ->helperText('Shown on the blog list and at the top of the post. Recommended 1200×630 px.')
                                    ->image()
                                    ->directory('blog')
                                    ->maxSize(4096),
                            ])
                            ->columnSpan(['lg' => 1]),
                    ])
                    ->columns(['lg' => 3]),
                SeoFields::section('post'),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('cover_image')->label('Cover')->square(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Post $record) => $record->slug),
                TextColumn::make('status')
                    ->badge()
                    ->state(fn (Post $record) => static::statusLabel($record))
                    ->color(fn (string $state) => match ($state) {
                        'Published' => 'success',
                        'Scheduled' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('published_at')->label('Publish date')->dateTime()->sortable(),
                TextColumn::make('createdBy.name')->label('Author')->description(fn (Post $record) => $record->created_at),
                TextColumn::make('updatedBy.name')
                    ->description(fn (Post $record) => $record->updated_at)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(Post::STATUSES),
            ])
            ->actions([
                // Drafts open as a preview for users who can edit them.
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Post $record) => $record->url, shouldOpenInNewTab: true),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * "Published", "Scheduled" (published with a future date) or "Draft".
     */
    public static function statusLabel(Post $post): string
    {
        return match (true) {
            $post->is_published => 'Published',
            $post->status === Post::STATUS_PUBLISHED => 'Scheduled',
            default => 'Draft',
        };
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
