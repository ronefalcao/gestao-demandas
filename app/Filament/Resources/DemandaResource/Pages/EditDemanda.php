<?php

namespace App\Filament\Resources\DemandaResource\Pages;

use App\Filament\Resources\DemandaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDemanda extends EditRecord
{
    protected static string $resource = DemandaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
