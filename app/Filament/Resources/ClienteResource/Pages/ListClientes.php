<?php

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Resources\ClienteResource;
use App\Models\Status;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListClientes extends ListRecords
{
    protected static string $resource = ClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        // Obter IDs dos status considerados concluídos
        $statusConcluidosIds = Status::whereIn('nome', ['Concluído', 'Homologada', 'Publicada'])
            ->pluck('id')
            ->toArray();

        // Carregar contagem de demandas não concluídas com eager loading
        return parent::getTableQuery()
            ->withCount(['demandas' => function ($query) use ($statusConcluidosIds) {
                if (!empty($statusConcluidosIds)) {
                    $query->whereNotIn('status_id', $statusConcluidosIds);
                }
            }]);
    }
}
