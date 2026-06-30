<?php

namespace App\Filament\Widgets;

use App\Models\Click;
use App\Models\Link;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class LinkStatsOverview extends BaseWidget
{
    protected static ?int $sort = -3;

    protected function getStats(): array
    {
        $userId = Auth::id();

        $links = Link::query()->where('user_id', $userId)->count();
        $clicks = Click::query()
            ->whereHas('link', fn ($query) => $query->where('user_id', $userId))
            ->count();

        return [
            Stat::make('My links', $links)
                ->description('Short links you have created')
                ->color('primary'),
            Stat::make('Total clicks', $clicks)
                ->description('Across all your links')
                ->color('success'),
        ];
    }
}
