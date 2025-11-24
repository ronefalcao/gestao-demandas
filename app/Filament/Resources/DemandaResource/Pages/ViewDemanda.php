<?php

namespace App\Filament\Resources\DemandaResource\Pages;

use App\Filament\Resources\DemandaResource;
use App\Models\Status;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewDemanda extends ViewRecord
{
    protected static string $resource = DemandaResource::class;

    public function getTitle(): string
    {
        return '';
    }

    protected function getHeaderActions(): array
    {
        $user = Auth::user();
        $demanda = $this->record->load('status');
        
        $actions = [
            Actions\EditAction::make()
                ->authorize(fn() => DemandaResource::canEdit($this->record)),
        ];

        // Adicionar botões para usuários comuns
        if ($user && $user->isUsuario() && $demanda->solicitante_id === $user->id) {
            // Botão "Solicitar" quando a demanda estiver em "Rascunho"
            if ($demanda->status && $demanda->status->nome === 'Rascunho') {
                $actions[] = Actions\Action::make('solicitar')
                    ->label('Solicitar')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Solicitar Demanda')
                    ->modalDescription('Tem certeza que deseja solicitar esta demanda? A demanda será enviada para análise.')
                    ->modalSubmitActionLabel('Sim, Solicitar')
                    ->action(function () {
                        $demanda = $this->record;
                        $statusSolicitada = Status::where('nome', 'Solicitada')->first();
                        if ($statusSolicitada) {
                            $demanda->update(['status_id' => $statusSolicitada->id]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Demanda solicitada com sucesso!')
                                ->success()
                                ->send();
                            
                            $this->redirect(static::getResource()::getUrl('view', ['record' => $demanda]));
                        }
                    });
            }
            
            // Botão "Cancelar Solicitação" quando a demanda estiver em "Solicitada"
            if ($demanda->status && $demanda->status->nome === 'Solicitada') {
                $actions[] = Actions\Action::make('cancelarSolicitacao')
                    ->label('Cancelar Solicitação')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar Solicitação')
                    ->modalDescription('Tem certeza que deseja cancelar a solicitação desta demanda? A demanda voltará para o status "Rascunho" e você poderá editá-la novamente.')
                    ->modalSubmitActionLabel('Sim, Cancelar Solicitação')
                    ->action(function () {
                        $demanda = $this->record;
                        $statusRascunho = Status::where('nome', 'Rascunho')->first();
                        if ($statusRascunho) {
                            $demanda->update(['status_id' => $statusRascunho->id]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Solicitação cancelada com sucesso!')
                                ->success()
                                ->send();
                            
                            $this->redirect(static::getResource()::getUrl('view', ['record' => $demanda]));
                        }
                    });
            }
        }

        return $actions;
    }
}
