<?php

namespace App\Filament\Widgets;

use App\Models\Demanda;
use App\Models\Status;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class DemandasPorStatusCustomWidget extends Widget
{
    protected static string $view = 'filament.widgets.demandas-por-status-custom-widget';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 2;
    
    public array $totais = [];
    
    public function mount(): void
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
        
        $statuses = Status::orderBy('ordem')
            ->get();
        
        foreach ($statuses as $status) {
            $countQuery = clone $baseQuery;
            $this->totais[] = [
                'id' => $status->id,
                'nome' => $status->nome,
                'cor' => $status->cor ?? '#6c757d',
                'total' => $countQuery->where('status_id', $status->id)->count(),
            ];
        }
    }
}

