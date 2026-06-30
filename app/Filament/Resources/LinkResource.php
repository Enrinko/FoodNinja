<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LinkResource\Pages;
use App\Filament\Resources\LinkResource\RelationManagers;
use App\Models\Link;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LinkResource extends Resource
{
    protected static ?string $model = Link::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $recordTitleAttribute = 'short_code';

    protected static ?string $modelLabel = 'link';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('original_url')
                    ->label('Original URL')
                    ->placeholder('https://example.com/page')
                    ->url()
                    ->required()
                    ->maxLength(2048)
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('short_code')
                    ->label('Short URL')
                    ->state(fn (Link $record): string => url('/'.$record->short_code))
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->icon('heroicon-m-link'),
                Infolists\Components\TextEntry::make('original_url')
                    ->label('Original URL')
                    ->url(fn (Link $record): string => $record->original_url)
                    ->openUrlInNewTab(),
                Infolists\Components\TextEntry::make('clicks_count')
                    ->label('Total clicks')
                    ->state(fn (Link $record): int => $record->clicks()->count())
                    ->badge()
                    ->color('success'),
                Infolists\Components\TextEntry::make('created_at')
                    ->label('Created')
                    ->dateTime(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('short_code')
                    ->label('Short URL')
                    ->state(fn (Link $record): string => url('/'.$record->short_code))
                    ->icon('heroicon-m-link')
                    ->copyable()
                    ->copyMessage('Short URL copied!')
                    ->searchable(),
                Tables\Columns\TextColumn::make('original_url')
                    ->label('Original URL')
                    ->limit(50)
                    ->tooltip(fn (Link $record): string => $record->original_url)
                    ->url(fn (Link $record): string => $record->original_url)
                    ->openUrlInNewTab()
                    ->searchable(),
                Tables\Columns\TextColumn::make('clicks_count')
                    ->label('Clicks')
                    ->counts('clicks')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No links yet')
            ->emptyStateDescription('Create your first short link to get started.');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ClicksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLinks::route('/'),
            'create' => Pages\CreateLink::route('/create'),
            'view' => Pages\ViewLink::route('/{record}'),
            'edit' => Pages\EditLink::route('/{record}/edit'),
        ];
    }

    /**
     * Restrict every query to the links owned by the authenticated user.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }
}
