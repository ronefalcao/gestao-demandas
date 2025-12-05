<?php

namespace App\Filament\Resources\DemandaResource\Pages;

use App\Filament\Resources\DemandaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListDemandas extends ListRecords
{
    protected static string $resource = DemandaResource::class;
    
    protected static string $view = 'filament-panels::resources.pages.list-demandas';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $user = Auth::user();
        $query = parent::getTableQuery();

        // Carregar relacionamentos necessários para evitar N+1 queries
        $query->with('status');

        // Filtrar demandas baseado no tipo de usuário e projetos associados
        if (!$user->isAdmin()) {
            $projetosIds = $user->projetos()->pluck('projetos.id');
            if ($projetosIds->isEmpty()) {
                // Se o usuário não tem projetos associados, não mostrar nenhuma demanda
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('projeto_id', $projetosIds);

                // Usuário comum só vê suas próprias demandas (que ele criou)
                // Analista vê todas as demandas dos projetos que tem acesso
                if ($user->isUsuario()) {
                    $query->where('solicitante_id', $user->id);
                }
                // Gestor e Analista veem todas as demandas dos projetos com acesso (sem filtro adicional)
            }
        }

        // Excluir rascunhos de outros usuários (apenas o criador vê seus rascunhos)
        $query->excludeRascunhosFromOthers($user->id);

        return $query;
    }
}