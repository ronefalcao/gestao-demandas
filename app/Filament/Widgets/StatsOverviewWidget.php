<?php

namespace App\Filament\Widgets;

use App\Models\Cliente;
use App\Models\Demanda;
use App\Models\Projeto;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        $user = Auth::user();
        
        // Base query para demandas
        $baseQuery = Demanda::query();
        
        if (!$user->isAdmin()) {
            $projetosIds = $user->projetos()->pluck('projetos.id');
            if ($projetosIds->isEmpty()) {
                $baseQuery->whereRaw('1 = 0');
            } else {
                $baseQuery->whereIn('projeto_id', $projetosIds);
                if ($user->isUsuario()) {
                    $baseQuery->where('solicitante_id', $user->id);
                }
            }
        }
        
        $totalDemandas = $baseQuery->count();
        
        if ($user->isAdmin()) {
            $totalUsuarios = User::count();
            $totalProjetos = Projeto::where('ativo', true)->count();
            $totalClientes = Cliente::count();
        } else {
            $totalUsuarios = 0;
            $totalProjetos = $user->projetos()->where('projetos.ativo', true)->count();
            $totalClientes = 0;
        }
        
        $stats = [];
        
        if ($user->isAdmin()) {
            $stats[] = Stat::make('Usuários', $totalUsuarios)
                ->description('Contas ativas no sistema')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary');
        }
        
        // Não exibir cards de Projetos e Demandas para Gestor e Usuário
        if (!$user->isGestor() && !$user->isUsuario()) {
            $stats[] = Stat::make('Projetos', $totalProjetos)
                ->description('Projetos cadastrados')
                ->descriptionIcon('heroicon-o-folder')
                ->color('success');
            
            $stats[] = Stat::make('Demandas', $totalDemandas)
                ->description('Itens no pipeline')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('info');
        }
        
        if ($user->isAdmin()) {
            $stats[] = Stat::make('Clientes', $totalClientes)
                ->description('Organizações atendidas')
                ->descriptionIcon('heroicon-o-building-office')
                ->color('warning');
        }
        
        return $stats;
    }
}

