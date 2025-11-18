<?php

namespace App\Filament\Widgets;

use App\Models\Feature;
use App\Models\Item;
use App\Models\Projeto;
use App\Models\Sprint;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class ItensStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        // Desabilitar este widget
        return false;
    }

    protected function getStats(): array
    {
        $user = Auth::user();

        // Base query para itens através de features
        $baseQuery = Item::query()
            ->join('features', 'itens.feature_id', '=', 'features.id')
            ->join('projetos', 'features.projeto_id', '=', 'projetos.id')
            ->where('projetos.ativo', true);

        if (!$user->isAdmin()) {
            $projetosIds = $user->projetos()->pluck('projetos.id');
            if ($projetosIds->isEmpty()) {
                $baseQuery->whereRaw('1 = 0');
            } else {
                $baseQuery->whereIn('projetos.id', $projetosIds);
            }
        }

        $totalItens = (clone $baseQuery)->count();
        $itensAbertos = (clone $baseQuery)->where('itens.status', 'aberto')->count();
        $itensFechados = (clone $baseQuery)->where('itens.status', 'fechado')->count();

        // Contar features
        $featuresQuery = Feature::query()
            ->join('projetos', 'features.projeto_id', '=', 'projetos.id')
            ->where('projetos.ativo', true);

        if (!$user->isAdmin()) {
            $projetosIds = $user->projetos()->pluck('projetos.id');
            if ($projetosIds->isEmpty()) {
                $featuresQuery->whereRaw('1 = 0');
            } else {
                $featuresQuery->whereIn('projetos.id', $projetosIds);
            }
        }

        $totalFeatures = $featuresQuery->count();

        // Contar sprints através de itens
        $sprintsQuery = Sprint::query()
            ->join('itens', 'sprints.id', '=', 'itens.sprint_id')
            ->join('features', 'itens.feature_id', '=', 'features.id')
            ->join('projetos', 'features.projeto_id', '=', 'projetos.id')
            ->where('projetos.ativo', true)
            ->whereNotNull('itens.sprint_id');

        if (!$user->isAdmin()) {
            $projetosIds = $user->projetos()->pluck('projetos.id');
            if ($projetosIds->isEmpty()) {
                $sprintsQuery->whereRaw('1 = 0');
            } else {
                $sprintsQuery->whereIn('projetos.id', $projetosIds);
            }
        }

        $totalSprints = $sprintsQuery->distinct('sprints.id')->count('sprints.id');

        $stats = [];

        $stats[] = Stat::make('Total de Itens', $totalItens)
            ->description('Itens cadastrados')
            ->descriptionIcon('heroicon-o-list-bullet')
            ->color('primary');

        $stats[] = Stat::make('Itens Abertos', $itensAbertos)
            ->description('Em andamento')
            ->descriptionIcon('heroicon-o-clock')
            ->color('warning');

        $stats[] = Stat::make('Itens Fechados', $itensFechados)
            ->description('Concluídos')
            ->descriptionIcon('heroicon-o-check-circle')
            ->color('success');

        if ($user->isAdmin() || $user->isPlanejador()) {
            $stats[] = Stat::make('Features', $totalFeatures)
                ->description('Features cadastradas')
                ->descriptionIcon('heroicon-o-sparkles')
                ->color('info');

            $stats[] = Stat::make('Sprints', $totalSprints)
                ->description('Sprints ativas')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('gray');
        }

        return $stats;
    }
}

