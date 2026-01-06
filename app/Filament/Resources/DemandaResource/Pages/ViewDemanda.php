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

    protected static string $view = 'filament.resources.pages.view-demanda';

    public function getTitle(): string
    {
        return '';
    }

    protected function getHeaderActions(): array
    {
        $user = Auth::user();
        $demanda = $this->record;
        
        // Carregar relacionamentos necessários
        $demanda->loadMissing(['status', 'projeto']);
        
        // Determinar para qual lista voltar baseado no status
        $urlVoltar = static::getResource()::getUrl('abertas');
        if ($demanda->status) {
            $statusNome = $demanda->status->nome;
            if (in_array($statusNome, ['Backlog', 'Em Desenvolvimento', 'Em Teste'])) {
                $urlVoltar = static::getResource()::getUrl('desenvolvimento');
            } elseif (in_array($statusNome, ['Cancelada', 'Concluído', 'Homologada'])) {
                $urlVoltar = static::getResource()::getUrl('concluidas');
            } elseif ($statusNome === 'Publicada') {
                $urlVoltar = static::getResource()::getUrl('publicadas');
            }
        }
        
        // Inicializar array de ações - SEMPRE começar com o botão Voltar
        $actions = [
            // Botão Voltar - sempre visível para todos
            Actions\Action::make('voltar')
                ->label('Voltar')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($urlVoltar),
            
            // Botão Atualizar (Editar) - verificar permissão
            Actions\EditAction::make()
                ->label('Atualizar')
                ->visible(fn() => DemandaResource::canEdit($demanda)),
            
            // Botão Excluir - verificar permissão
            Actions\DeleteAction::make()
                ->visible(fn() => DemandaResource::canDelete($demanda)),
        ];

        // Se não houver usuário ou status, retornar apenas os botões básicos
        if (!$user || !$demanda->status) {
            return $actions;
        }

        $statusAtual = $demanda->status->nome;
        $isAdmin = $user->canManageSystem();
        $isAnalista = $user->isAnalista();
        $isUsuario = $user->isUsuario();
        $isProprioUsuario = $isUsuario && $demanda->solicitante_id === $user->id;

        // Botões para usuários comuns (apenas suas próprias demandas)
        if ($isProprioUsuario) {
            // Botão "Solicitar" quando a demanda estiver em "Rascunho"
            if ($statusAtual === 'Rascunho') {
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
            if ($statusAtual === 'Solicitada') {
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

        // Botões para Administradores e Analistas
        if ($isAdmin || $isAnalista) {
            // Verificar se analista tem acesso ao projeto da demanda
            if ($isAnalista) {
                $projetosIds = $user->projetos()->pluck('projetos.id');
                if (!in_array($demanda->projeto_id, $projetosIds->toArray())) {
                    return $actions; // Analista sem acesso ao projeto não vê botões
                }
            }

            // Botão para avançar status (próximo status na ordem)
            $statuses = Status::orderBy('ordem')->get();
            $statusAtualObj = $statuses->firstWhere('nome', $statusAtual);
            
            if ($statusAtualObj) {
                $ordemAtual = $statusAtualObj->ordem;
                $proximoStatus = $statuses->firstWhere('ordem', $ordemAtual + 1);
                
                if ($proximoStatus && $proximoStatus->nome !== 'Cancelada') {
                    $actions[] = Actions\Action::make('avancarStatus')
                        ->label('Avançar para ' . $proximoStatus->nome)
                        ->icon('heroicon-o-arrow-right')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Avançar Status')
                        ->modalDescription("Tem certeza que deseja avançar esta demanda para o status '{$proximoStatus->nome}'?")
                        ->modalSubmitActionLabel('Sim, Avançar')
                        ->action(function () use ($proximoStatus) {
                            $demanda = $this->record;
                            $demanda->update(['status_id' => $proximoStatus->id]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title("Status alterado para '{$proximoStatus->nome}' com sucesso!")
                                ->success()
                                ->send();
                            
                            $this->redirect(static::getResource()::getUrl('view', ['record' => $demanda]));
                        });
                }
            }

            // Botão para voltar status (status anterior na ordem)
            if ($statusAtualObj) {
                $ordemAtual = $statusAtualObj->ordem;
                $statusAnterior = $statuses->firstWhere('ordem', $ordemAtual - 1);
                
                if ($statusAnterior && $statusAnterior->nome !== 'Cancelada') {
                    $actions[] = Actions\Action::make('voltarStatus')
                        ->label('Voltar para ' . $statusAnterior->nome)
                        ->icon('heroicon-o-arrow-left')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Voltar Status')
                        ->modalDescription("Tem certeza que deseja voltar esta demanda para o status '{$statusAnterior->nome}'?")
                        ->modalSubmitActionLabel('Sim, Voltar')
                        ->action(function () use ($statusAnterior) {
                            $demanda = $this->record;
                            $demanda->update(['status_id' => $statusAnterior->id]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title("Status alterado para '{$statusAnterior->nome}' com sucesso!")
                                ->success()
                                ->send();
                            
                            $this->redirect(static::getResource()::getUrl('view', ['record' => $demanda]));
                        });
                }
            }

            // Botão para cancelar demanda (apenas se não estiver cancelada)
            if ($statusAtual !== 'Cancelada') {
                $actions[] = Actions\Action::make('cancelarDemanda')
                    ->label('Cancelar Demanda')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar Demanda')
                    ->modalDescription('Tem certeza que deseja cancelar esta demanda? Esta ação pode ser revertida posteriormente.')
                    ->modalSubmitActionLabel('Sim, Cancelar')
                    ->action(function () {
                        $demanda = $this->record;
                        $statusCancelada = Status::where('nome', 'Cancelada')->first();
                        if ($statusCancelada) {
                            $demanda->update(['status_id' => $statusCancelada->id]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Demanda cancelada com sucesso!')
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
