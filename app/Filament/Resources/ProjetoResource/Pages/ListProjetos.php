<?php

namespace App\Filament\Resources\ProjetoResource\Pages;

use App\Filament\Resources\ProjetoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListProjetos extends ListRecords
{
    protected static string $resource = ProjetoResource::class;

    public function getTitle(): string
    {
        return '';
    }

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

        // Filtrar projetos baseado no tipo de usuário
        if (!$user->isAdmin()) {
            // Planejador e Gestor só veem projetos aos quais têm acesso
            $projetosIds = $user->projetos()->pluck('projetos.id');
            if ($projetosIds->isEmpty()) {
                // Se o usuário não tem projetos associados, não mostrar nenhum projeto
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('id', $projetosIds);
            }
        }

        return $query;
    }
}
