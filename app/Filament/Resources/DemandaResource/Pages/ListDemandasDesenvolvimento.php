<?php

namespace App\Filament\Resources\DemandaResource\Pages;

use App\Filament\Resources\DemandaResource;
use App\Models\Status;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListDemandasDesenvolvimento extends ListRecords
{
    protected static string $resource = DemandaResource::class;

    protected static ?string $navigationLabel = 'Desenvolvimento';

    protected static ?string $navigationGroup = 'Demandas';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';

    protected static ?string $title = 'Demandas em Desenvolvimento';

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

        // Filtrar apenas status "Desenvolvimento" (Backlog, Planejamento, Em Desenvolvimento, Em Teste)
        $statusDesenvolvimento = Status::whereIn('nome', ['Backlog', 'Planejamento', 'Em Desenvolvimento', 'Em Teste'])->pluck('id');
        $query->whereIn('status_id', $statusDesenvolvimento);

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

