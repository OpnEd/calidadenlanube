<?php

namespace App\Filament\Widgets\Quality;

use App\Models\Quality\Improvement\Action;
use App\Models\Quality\Improvement\Finding;
use App\Models\Quality\Improvement\Plan;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ImprovementOverview extends BaseWidget
{
    protected static ?int $sort = 5;

    protected function getStats(): array
    {
        $teamId = Filament::getTenant()?->id;

        $plansQuery = Plan::query()->when($teamId, fn ($q) => $q->where('team_id', $teamId));
        $actionsQuery = Action::query()->when($teamId, fn ($q) => $q->where('team_id', $teamId));
        $findingsQuery = Finding::query()->when($teamId, fn ($q) => $q->where('team_id', $teamId));

        $activePlans = (clone $plansQuery)->whereIn('status', ['activo', 'en_verificacion', 'reabierto'])->count();
        $overdueActions = (clone $actionsQuery)
            ->whereDate('due_date', '<', now()->toDateString())
            ->whereNotIn('status', ['terminada'])
            ->count();
        $openFindings = (clone $findingsQuery)->where('status', '!=', 'cerrado')->count();

        $closedInTime = (clone $plansQuery)
            ->where('status', 'cerrado')
            ->whereNotNull('closed_at')
            ->whereNotNull('due_date')
            ->whereColumn('closed_at', '<=', 'due_date')
            ->count();

        $closedTotal = (clone $plansQuery)
            ->where('status', 'cerrado')
            ->count();

        $closeOnTimeRate = $closedTotal > 0 ? round(($closedInTime / $closedTotal) * 100, 1) : 0;

        return [
            Stat::make('Planes activos', (string) $activePlans)
                ->description('Planes en ejecucion o verificacion')
                ->descriptionIcon('phosphor-target')
                ->color('success'),
            Stat::make('Acciones vencidas', (string) $overdueActions)
                ->description('Acciones fuera de fecha compromiso')
                ->descriptionIcon('phosphor-warning-circle')
                ->color($overdueActions > 0 ? 'danger' : 'success'),
            Stat::make('Hallazgos abiertos', (string) $openFindings)
                ->description('Pendientes de cierre')
                ->descriptionIcon('phosphor-magnifying-glass')
                ->color('warning'),
            Stat::make('Cierre en fecha', $closeOnTimeRate . '%')
                ->description('Planes cerrados a tiempo')
                ->descriptionIcon('phosphor-check-circle')
                ->color($closeOnTimeRate >= 80 ? 'success' : 'warning'),
        ];
    }
}

