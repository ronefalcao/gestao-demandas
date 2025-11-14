<?php

namespace App\Filament\Resources\DemandaResource\Pages;

use App\Filament\Resources\DemandaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDemandas extends ListRecords
{
    protected static string $resource = DemandaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
