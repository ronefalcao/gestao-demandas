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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        // Se o usuário for do tipo "usuario", definir automaticamente o solicitante e status
        if ($user && $user->isUsuario()) {
            $data['solicitante_id'] = $user->id;
            $statusSolicitada = Status::where('nome', 'Solicitada')->first();
            if ($statusSolicitada) {
                $data['status_id'] = $statusSolicitada->id;
            }
        }

        return $data;
    }
}
