<?php

namespace zaheensayyed\FilamentCms\Resources;

use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use zaheensayyed\FilamentCms\Models\ContactFormSubmission;
use zaheensayyed\FilamentCms\Resources\ContactSubmissionResource\Pages;

/**
 * Read-only log of contact form submissions: view and delete only.
 */
class ContactSubmissionResource extends Resource
{
    protected static ?string $model = ContactFormSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    protected static ?string $navigationLabel = 'Contact Submissions';

    protected static ?string $modelLabel = 'contact submission';

    protected static ?string $slug = 'contact-submissions';

    public const MAIL_STATUS_COLORS = [
        ContactFormSubmission::MAIL_SENT => 'success',
        ContactFormSubmission::MAIL_FAILED => 'danger',
        ContactFormSubmission::MAIL_PENDING => 'warning',
    ];

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    /**
     * Submissions received in the last 7 days.
     */
    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('created_at', '>=', now()->subDays(7))->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Received in the last 7 days';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable()->copyable(),
                TextColumn::make('subject')->placeholder('—')->limit(40)->searchable(),
                TextColumn::make('mail_status')
                    ->label('Mail')
                    ->badge()
                    ->color(fn (string $state): string => static::MAIL_STATUS_COLORS[$state] ?? 'gray')
                    ->tooltip(fn (ContactFormSubmission $record): ?string => $record->mail_error),
                TextColumn::make('created_at')->label('Received')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('mail_status')
                    ->label('Mail status')
                    ->options([
                        ContactFormSubmission::MAIL_SENT => 'Sent',
                        ContactFormSubmission::MAIL_FAILED => 'Failed',
                        ContactFormSubmission::MAIL_PENDING => 'Pending',
                    ]),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')->label('Received from'),
                        DatePicker::make('until')->label('Received until'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date)))
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = 'From ' . $data['from'];
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = 'Until ' . $data['until'];
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email')->copyable(),
                        TextEntry::make('phone')->placeholder('—'),
                        TextEntry::make('subject')->placeholder('—'),
                    ]),
                TextEntry::make('message')
                    ->columnSpanFull()
                    ->extraAttributes(['style' => 'white-space: pre-wrap']),
                Section::make('Delivery')
                    ->compact()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('mail_status')
                            ->label('Mail status')
                            ->badge()
                            ->color(fn (string $state): string => static::MAIL_STATUS_COLORS[$state] ?? 'gray'),
                        TextEntry::make('created_at')->label('Received')->dateTime(),
                        TextEntry::make('mail_error')->label('Mail error')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('ip_address')->label('IP address')->placeholder('—'),
                        TextEntry::make('user_agent')->label('User agent')->placeholder('—'),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactSubmissions::route('/'),
        ];
    }
}
