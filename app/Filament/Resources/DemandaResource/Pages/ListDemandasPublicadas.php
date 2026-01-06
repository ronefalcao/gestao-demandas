<?php

namespace App\Filament\Resources\DemandaResource\Pages;

use App\Filament\Resources\DemandaResource;
use App\Models\Status;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListDemandasPublicadas extends ListRecords
{
    protected static string $resource = DemandaResource::class;

    protected static ?string $navigationLabel = 'Publicadas';

    protected static ?string $navigationGroup = 'Demandas';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $title = 'Demandas Publicadas';

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        $user = Auth::user();
        return $user && ($user->canManageSystem() || $user->isGestor() || $user->isUsuario() || $user->isAnalista());
    }

    protected function getHeaderActions(): array
    {
        // Não mostrar botão de criar nesta página
        return [];
    }

    protected function getTableQuery(): Builder
    {
        $user = Auth::user();
        $query = parent::getTableQuery();

        // Carregar relacionamentos necessários para evitar N+1 queries
        $query->with('status');

        // Filtrar apenas status "Publicada"
        $statusPublicada = Status::where('nome', 'Publicada')->first();
        if ($statusPublicada) {
            $query->where('status_id', $statusPublicada->id);
        } else {
            $query->whereRaw('1 = 0');
        }

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

