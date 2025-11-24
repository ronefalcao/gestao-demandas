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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->authorize(fn(Demanda $record) => static::canEdit($record)),
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
