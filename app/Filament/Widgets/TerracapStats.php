<?php

namespace App\Filament\Widgets;

use App\Models\DataSource;
use App\Models\Lot;
use App\Models\Notice;
use App\Models\SyncRun;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TerracapStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Lotes publicados', Lot::query()->published()->count())
                ->description(Lot::query()->where('status', 'draft')->count().' em rascunho')
                ->color('success'),
            Stat::make('Editais', Notice::query()->count())
                ->description(Notice::query()->where('status', 'open')->count().' abertos')
                ->color('info'),
            Stat::make('Fontes ativas', DataSource::query()->where('status', 'active')->count())
                ->description(DataSource::query()->count().' cadastradas')
                ->color('warning'),
            Stat::make('Última sincronização', SyncRun::query()->latest('finished_at')->first()?->finished_at?->diffForHumans() ?? 'Nenhuma')
                ->description(SyncRun::query()->where('status', 'failed')->count().' falhas registradas'),
        ];
    }
}
