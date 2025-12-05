<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DemandaResource\Pages;
use App\Filament\Resources\DemandaResource\RelationManagers;
use App\Models\Demanda;
use App\Models\Cliente;
use App\Models\Projeto;
use App\Models\Status;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class DemandaResource extends Resource
{
    protected static ?string $model = Demanda::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Demandas';

    protected static ?int $navigationSort = 7;

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        // Planejador não tem acesso a demandas
        return $user && ($user->canManageSystem() || $user->isGestor() || $user->isUsuario() || $user->isAnalista());
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user && ($user->canManageSystem() || $user->isGestor() || $user->isUsuario() || $user->isAnalista());
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();
        // Administrador e Analista podem editar
        if (!$user) {
            return false;
        }

        // Garantir que o record tenha os relacionamentos necessários carregados
        if ($record instanceof \Illuminate\Database\Eloquent\Model) {
            $record->loadMissing(['status', 'projeto']);
        }

        // Verificar se o status é "Solicitada" ou posterior (ordem >= 1)
        $statusOrdem = $record->status ? $record->status->ordem : 0;
        $isSolicitadaOuPosterior = $statusOrdem >= 1;

        // Se o status for "Solicitada" ou posterior, apenas administrador e analista podem editar
        if ($isSolicitadaOuPosterior) {
            if ($user->canManageSystem()) {
                return true;
            }

            // Analista pode editar demandas dos projetos que tem acesso
            if ($user->isAnalista()) {
                $projetosIds = $user->projetos()->pluck('projetos.id');
                return in_array($record->projeto_id, $projetosIds->toArray());
            }

            // Usuário comum não pode editar demandas com status "Solicitada" ou posterior
            return false;
        }

        // Para status "Rascunho" (ordem = 0), manter a lógica original
        if ($user->canManageSystem()) {
            return true;
        }

        // Analista pode editar demandas dos projetos que tem acesso
        if ($user->isAnalista()) {
            $projetosIds = $user->projetos()->pluck('projetos.id');
            return in_array($record->projeto_id, $projetosIds->toArray());
        }

        // Usuário comum pode editar apenas suas próprias demandas com status "Rascunho"
        if ($user->isUsuario()) {
            if ($record->solicitante_id !== $user->id) {
                return false;
            }
            return $record->status && $record->status->nome === 'Rascunho';
        }

        return false;
    }

    public static function canDelete($record): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        if ($user->canManageSystem()) {
            return true;
        }

        // Usuário comum pode excluir apenas suas próprias demandas com status "Rascunho"
        if ($user->isUsuario()) {
            if ($record->solicitante_id !== $user->id) {
                return false;
            }
            $record->loadMissing('status');
            return $record->status && $record->status->nome === 'Rascunho';
        }

        return false;
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();

        return $form
            ->schema([
                Forms\Components\Section::make('Informações Básicas')
                    ->schema([
                        Forms\Components\TextInput::make('numero')
                            ->label('Número')
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\DatePicker::make('data')
                            ->label('Data')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Forms\Components\Select::make('cliente_id')
                            ->label('Cliente')
                            ->relationship('cliente', 'nome')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('projeto_id')
                            ->label('Projeto')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(function () use ($user) {
                                if (!$user) {
                                    return [];
                                }
                                if ($user->isAdmin()) {
                                    return Projeto::where('ativo', true)->pluck('nome', 'id');
                                }
                                // Usuários não-admin só veem projetos aos quais têm acesso
                                return $user->projetos()
                                    ->where('projetos.ativo', true)
                                    ->select('projetos.id', 'projetos.nome')
                                    ->pluck('projetos.nome', 'projetos.id');
                            }),
                        Forms\Components\TextInput::make('modulo')
                            ->label('Módulo')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Detalhes')
                    ->schema([
                        Forms\Components\Textarea::make('descricao')
                            ->label('Descrição')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('solicitante_id')
                            ->label('Solicitante')
                            ->relationship('solicitante', 'nome')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(fn() => $user?->id)
                            ->disabled(fn() => $user?->isUsuario() ?? false),
                        Forms\Components\Select::make('responsavel_id')
                            ->label('Responsável')
                            ->relationship('responsavel', 'nome')
                            ->searchable()
                            ->preload()
                            ->visible(fn() => $user && (!$user->isUsuario())),
                        Forms\Components\Select::make('status_id')
                            ->label('Status')
                            ->relationship('status', 'nome', fn($query) => $query->orderBy('ordem'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(function () use ($user) {
                                if (!$user || $user->isUsuario()) {
                                    // Usuário comum pode ver apenas Rascunho (na edição sempre será Rascunho)
                                    return Status::where('nome', 'Rascunho')
                                        ->orderBy('ordem')
                                        ->pluck('nome', 'id');
                                }
                                // Analista e outros perfis podem ver todos os status
                                return Status::orderBy('ordem')->pluck('nome', 'id');
                            })
                            ->default(fn() => Status::where('nome', 'Rascunho')->first()?->id)
                            ->disabled(fn() => $user && $user->isUsuario()),
                        Forms\Components\Select::make('prioridade')
                            ->label('Prioridade')
                            ->options([
                                'baixa' => 'Baixa',
                                'media' => 'Média',
                                'alta' => 'Alta',
                            ])
                            ->required()
                            ->default('media')
                            ->native(false),
                        Forms\Components\Textarea::make('observacao')
                            ->label('Observação')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();

        $columns = [
            Tables\Columns\TextColumn::make('numero')
                ->label('Número')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('data')
                ->label('Data')
                ->date('d/m/Y')
                ->sortable(),
            Tables\Columns\TextColumn::make('cliente.nome')
                ->label('Cliente')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('projeto.nome')
                ->label('Projeto')
                ->searchable()
                ->sortable()
                ->toggleable(),
            Tables\Columns\TextColumn::make('modulo')
                ->label('Módulo')
                ->searchable()
                ->limit(30),
            Tables\Columns\TextColumn::make('status.nome')
                ->label('Status')
                ->badge()
                ->color(fn($record) => $record->status->cor ?? 'gray')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('prioridade')
                ->label('Prioridade')
                ->badge()
                ->formatStateUsing(fn($state) => match ($state) {
                    'baixa' => 'Baixa',
                    'media' => 'Média',
                    'alta' => 'Alta',
                    default => ucfirst($state),
                })
                ->color(fn($state) => match ($state) {
                    'baixa' => 'success', // verde
                    'media' => 'warning', // amarela
                    'alta' => 'danger', // vermelha
                    default => 'gray',
                })
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('descricao')
                ->label('Descrição')
                ->limit(50)
                ->searchable()
                ->toggleable(),
        ];

        // Adicionar coluna solicitante apenas se não for usuário comum
        if (!$user || !$user->isUsuario()) {
            $columns[] = Tables\Columns\TextColumn::make('solicitante.nome')
                ->label('Solicitante')
                ->searchable()
                ->sortable()
                ->toggleable();
        }

        $columns[] = Tables\Columns\TextColumn::make('responsavel.nome')
            ->label('Responsável')
            ->searchable()
            ->sortable()
            ->toggleable();

        $columns[] = Tables\Columns\TextColumn::make('created_at')
            ->label('Criado em')
            ->dateTime('d/m/Y H:i')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);

        return $table
            ->recordUrl(fn(Demanda $record): string => static::getUrl('view', ['record' => $record]))
            ->columns($columns)
            ->filters([
                Tables\Filters\SelectFilter::make('status_id')
                    ->label('Status')
                    ->relationship('status', 'nome', fn($query) => $query->orderBy('ordem'))
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('prioridade')
                    ->label('Prioridade')
                    ->options([
                        'baixa' => 'Baixa',
                        'media' => 'Média',
                        'alta' => 'Alta',
                    ]),
                Tables\Filters\SelectFilter::make('cliente_id')
                    ->label('Cliente')
                    ->relationship('cliente', 'nome')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('projeto_id')
                    ->label('Projeto')
                    ->searchable()
                    ->preload()
                    ->options(function () use ($user) {
                        if (!$user) {
                            return [];
                        }
                        if ($user->isAdmin()) {
                            return Projeto::where('ativo', true)->pluck('nome', 'id');
                        }
                        // Usuários não-admin só veem projetos aos quais têm acesso
                        return $user->projetos()
                            ->where('projetos.ativo', true)
                            ->select('projetos.id', 'projetos.nome')
                            ->pluck('projetos.nome', 'projetos.id');
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->authorize(fn(Demanda $record) => static::canEdit($record)),
                // Ação para usuários comuns: Solicitar
                Tables\Actions\Action::make('solicitar')
                    ->label('Solicitar')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Solicitar Demanda')
                    ->modalDescription('Tem certeza que deseja solicitar esta demanda? A demanda será enviada para análise.')
                    ->modalSubmitActionLabel('Sim, Solicitar')
                    ->action(function (Demanda $record) {
                        $statusSolicitada = Status::where('nome', 'Solicitada')->first();
                        if ($statusSolicitada) {
                            $record->update(['status_id' => $statusSolicitada->id]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Demanda solicitada com sucesso!')
                                ->success()
                                ->send();
                        }
                    })
                    ->visible(function (Demanda $record) use ($user) {
                        if (!$user || !$user->isUsuario()) {
                            return false;
                        }
                        $record->loadMissing('status');
                        return $record->solicitante_id === $user->id 
                            && $record->status 
                            && $record->status->nome === 'Rascunho';
                    }),
                // Ação para usuários comuns: Cancelar Solicitação
                Tables\Actions\Action::make('cancelarSolicitacao')
                    ->label('Cancelar Solicitação')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar Solicitação')
                    ->modalDescription('Tem certeza que deseja cancelar a solicitação desta demanda? A demanda voltará para o status "Rascunho" e você poderá editá-la novamente.')
                    ->modalSubmitActionLabel('Sim, Cancelar Solicitação')
                    ->action(function (Demanda $record) {
                        $statusRascunho = Status::where('nome', 'Rascunho')->first();
                        if ($statusRascunho) {
                            $record->update(['status_id' => $statusRascunho->id]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Solicitação cancelada com sucesso!')
                                ->success()
                                ->send();
                        }
                    })
                    ->visible(function (Demanda $record) use ($user) {
                        if (!$user || !$user->isUsuario()) {
                            return false;
                        }
                        $record->loadMissing('status');
                        return $record->solicitante_id === $user->id 
                            && $record->status 
                            && $record->status->nome === 'Solicitada';
                    }),
                // Ação para Admin/Analista: Avançar Status
                Tables\Actions\Action::make('avancarStatus')
                    ->label(function (Demanda $record) {
                        $record->loadMissing('status');
                        if (!$record->status) {
                            return 'Avançar Status';
                        }
                        $statuses = Status::orderBy('ordem')->get();
                        $statusAtualObj = $statuses->firstWhere('nome', $record->status->nome);
                        if ($statusAtualObj) {
                            $proximoStatus = $statuses->firstWhere('ordem', $statusAtualObj->ordem + 1);
                            if ($proximoStatus && $proximoStatus->nome !== 'Cancelada') {
                                return 'Avançar para ' . $proximoStatus->nome;
                            }
                        }
                        return 'Avançar Status';
                    })
                    ->icon('heroicon-o-arrow-right')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Avançar Status')
                    ->modalDescription(function (Demanda $record) {
                        $record->loadMissing('status');
                        if (!$record->status) {
                            return '';
                        }
                        $statuses = Status::orderBy('ordem')->get();
                        $statusAtualObj = $statuses->firstWhere('nome', $record->status->nome);
                        if ($statusAtualObj) {
                            $proximoStatus = $statuses->firstWhere('ordem', $statusAtualObj->ordem + 1);
                            if ($proximoStatus && $proximoStatus->nome !== 'Cancelada') {
                                return "Tem certeza que deseja avançar esta demanda para o status '{$proximoStatus->nome}'?";
                            }
                        }
                        return '';
                    })
                    ->modalSubmitActionLabel('Sim, Avançar')
                    ->action(function (Demanda $record) {
                        $record->loadMissing('status');
                        if (!$record->status) {
                            return;
                        }
                        $statuses = Status::orderBy('ordem')->get();
                        $statusAtualObj = $statuses->firstWhere('nome', $record->status->nome);
                        if ($statusAtualObj) {
                            $proximoStatus = $statuses->firstWhere('ordem', $statusAtualObj->ordem + 1);
                            if ($proximoStatus && $proximoStatus->nome !== 'Cancelada') {
                                $record->update(['status_id' => $proximoStatus->id]);
                                
                                \Filament\Notifications\Notification::make()
                                    ->title("Status alterado para '{$proximoStatus->nome}' com sucesso!")
                                    ->success()
                                    ->send();
                            }
                        }
                    })
                    ->visible(function (Demanda $record) use ($user) {
                        if (!$user) {
                            return false;
                        }
                        $isAdmin = $user->canManageSystem();
                        $isAnalista = $user->isAnalista();
                        
                        if (!$isAdmin && !$isAnalista) {
                            return false;
                        }
                        
                        if ($isAnalista) {
                            $projetosIds = $user->projetos()->pluck('projetos.id');
                            if (!in_array($record->projeto_id, $projetosIds->toArray())) {
                                return false;
                            }
                        }
                        
                        $record->loadMissing('status');
                        if (!$record->status) {
                            return false;
                        }
                        
                        $statuses = Status::orderBy('ordem')->get();
                        $statusAtualObj = $statuses->firstWhere('nome', $record->status->nome);
                        if ($statusAtualObj) {
                            $proximoStatus = $statuses->firstWhere('ordem', $statusAtualObj->ordem + 1);
                            return $proximoStatus && $proximoStatus->nome !== 'Cancelada';
                        }
                        
                        return false;
                    }),
                // Ação para Admin/Analista: Voltar Status
                Tables\Actions\Action::make('voltarStatus')
                    ->label(function (Demanda $record) {
                        $record->loadMissing('status');
                        if (!$record->status) {
                            return 'Voltar Status';
                        }
                        $statuses = Status::orderBy('ordem')->get();
                        $statusAtualObj = $statuses->firstWhere('nome', $record->status->nome);
                        if ($statusAtualObj) {
                            $statusAnterior = $statuses->firstWhere('ordem', $statusAtualObj->ordem - 1);
                            if ($statusAnterior && $statusAnterior->nome !== 'Cancelada') {
                                return 'Voltar para ' . $statusAnterior->nome;
                            }
                        }
                        return 'Voltar Status';
                    })
                    ->icon('heroicon-o-arrow-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Voltar Status')
                    ->modalDescription(function (Demanda $record) {
                        $record->loadMissing('status');
                        if (!$record->status) {
                            return '';
                        }
                        $statuses = Status::orderBy('ordem')->get();
                        $statusAtualObj = $statuses->firstWhere('nome', $record->status->nome);
                        if ($statusAtualObj) {
                            $statusAnterior = $statuses->firstWhere('ordem', $statusAtualObj->ordem - 1);
                            if ($statusAnterior && $statusAnterior->nome !== 'Cancelada') {
                                return "Tem certeza que deseja voltar esta demanda para o status '{$statusAnterior->nome}'?";
                            }
                        }
                        return '';
                    })
                    ->modalSubmitActionLabel('Sim, Voltar')
                    ->action(function (Demanda $record) {
                        $record->loadMissing('status');
                        if (!$record->status) {
                            return;
                        }
                        $statuses = Status::orderBy('ordem')->get();
                        $statusAtualObj = $statuses->firstWhere('nome', $record->status->nome);
                        if ($statusAtualObj) {
                            $statusAnterior = $statuses->firstWhere('ordem', $statusAtualObj->ordem - 1);
                            if ($statusAnterior && $statusAnterior->nome !== 'Cancelada') {
                                $record->update(['status_id' => $statusAnterior->id]);
                                
                                \Filament\Notifications\Notification::make()
                                    ->title("Status alterado para '{$statusAnterior->nome}' com sucesso!")
                                    ->success()
                                    ->send();
                            }
                        }
                    })
                    ->visible(function (Demanda $record) use ($user) {
                        if (!$user) {
                            return false;
                        }
                        $isAdmin = $user->canManageSystem();
                        $isAnalista = $user->isAnalista();
                        
                        if (!$isAdmin && !$isAnalista) {
                            return false;
                        }
                        
                        if ($isAnalista) {
                            $projetosIds = $user->projetos()->pluck('projetos.id');
                            if (!in_array($record->projeto_id, $projetosIds->toArray())) {
                                return false;
                            }
                        }
                        
                        $record->loadMissing('status');
                        if (!$record->status) {
                            return false;
                        }
                        
                        $statuses = Status::orderBy('ordem')->get();
                        $statusAtualObj = $statuses->firstWhere('nome', $record->status->nome);
                        if ($statusAtualObj) {
                            $statusAnterior = $statuses->firstWhere('ordem', $statusAtualObj->ordem - 1);
                            return $statusAnterior && $statusAnterior->nome !== 'Cancelada';
                        }
                        
                        return false;
                    }),
                // Ação para Admin/Analista: Cancelar Demanda
                Tables\Actions\Action::make('cancelarDemanda')
                    ->label('Cancelar Demanda')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar Demanda')
                    ->modalDescription('Tem certeza que deseja cancelar esta demanda? Esta ação pode ser revertida posteriormente.')
                    ->modalSubmitActionLabel('Sim, Cancelar')
                    ->action(function (Demanda $record) {
                        $statusCancelada = Status::where('nome', 'Cancelada')->first();
                        if ($statusCancelada) {
                            $record->update(['status_id' => $statusCancelada->id]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Demanda cancelada com sucesso!')
                                ->success()
                                ->send();
                        }
                    })
                    ->visible(function (Demanda $record) use ($user) {
                        if (!$user) {
                            return false;
                        }
                        $isAdmin = $user->canManageSystem();
                        $isAnalista = $user->isAnalista();
                        
                        if (!$isAdmin && !$isAnalista) {
                            return false;
                        }
                        
                        if ($isAnalista) {
                            $projetosIds = $user->projetos()->pluck('projetos.id');
                            if (!in_array($record->projeto_id, $projetosIds->toArray())) {
                                return false;
                            }
                        }
                        
                        $record->loadMissing('status');
                        return $record->status && $record->status->nome !== 'Cancelada';
                    }),
                    Tables\Actions\DeleteAction::make()
                        ->visible(fn(Demanda $record) => static::canDelete($record)),
                ])
                    ->label('Ações')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => Auth::user()?->canManageSystem() ?? false),
                ]),
            ])
            ->defaultSort('data', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ArquivosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDemandas::route('/'),
            'create' => Pages\CreateDemanda::route('/create'),
            'view' => Pages\ViewDemanda::route('/{record}'),
            'edit' => Pages\EditDemanda::route('/{record}/edit'),
        ];
    }
}
