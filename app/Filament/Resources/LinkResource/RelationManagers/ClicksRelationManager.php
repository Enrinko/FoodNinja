<?php

namespace App\Filament\Resources\LinkResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ClicksRelationManager extends RelationManager
{
    protected static string $relationship = 'clicks';

    protected static ?string $title = 'Clicks';

    protected static ?string $icon = 'heroicon-o-cursor-arrow-ripple';

    /**
     * Clicks are recorded automatically on redirect, so this list is read-only.
     */
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('ip_address')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date / time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_agent')
                    ->label('User agent')
                    ->limit(40)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('referer')
                    ->label('Referer')
                    ->limit(30)
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }
}
