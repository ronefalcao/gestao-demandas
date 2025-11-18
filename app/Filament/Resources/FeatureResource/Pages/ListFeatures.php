<?php

namespace App\Filament\Resources\FeatureResource\Pages;

use App\Filament\Resources\FeatureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListFeatures extends ListRecords
{
    protected static string $resource = FeatureResource::class;

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

        // Filtrar features baseado no tipo de usuário
        if (!$user->isAdmin()) {
            // Planejador só vê features dos projetos aos quais tem acesso
            $projetosIds = $user->projetos()->pluck('projetos.id');
            if ($projetosIds->isEmpty()) {
                // Se o usuário não tem projetos associados, não mostrar nenhuma feature
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('projeto_id', $projetosIds);
            }
        }

        // Ordenar por projeto e depois por número (sequencial por projeto)
        $query->orderBy('projeto_id', 'asc')
            ->orderByRaw('CAST(SPLIT_PART(numero, \'.\', 1) AS INTEGER) ASC');

        return $query;
    }
}
