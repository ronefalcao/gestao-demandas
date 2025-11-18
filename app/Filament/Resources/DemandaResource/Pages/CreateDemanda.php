<?php

namespace App\Filament\Resources\DemandaResource\Pages;

use App\Filament\Resources\DemandaResource;
use App\Models\Status;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateDemanda extends CreateRecord
{
    protected static string $resource = DemandaResource::class;

    public function getTitle(): string
    {
        return '';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        // Se o usuário for do tipo "usuario", definir automaticamente o solicitante e status
        if ($user && $user->isUsuario()) {
            $data['solicitante_id'] = $user->id;
            // Criar demanda com status "Rascunho" para permitir edição/exclusão
            $statusRascunho = Status::where('nome', 'Rascunho')->first();
            if ($statusRascunho) {
                $data['status_id'] = $statusRascunho->id;
            }
        }

        return $data;
    }
}
