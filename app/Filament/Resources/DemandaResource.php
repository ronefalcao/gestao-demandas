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

    protected static ?int $navigationSort = 5;

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && ($user->canManageSystem() || $user->isGestor() || $user->isUsuario());
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user && ($user->canManageSystem() || $user->isGestor() || $user->isUsuario());
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();
        // Gestor não pode editar, apenas Administrador pode
        return $user && $user->canManageSystem();
    }

    public static function canDelete($record): bool
    {
        $user = Auth::user();
        return $user && $user->canManageSystem();
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
                            ->visible(fn() => $user && !$user->isUsuario()),
                        Forms\Components\Select::make('status_id')
                            ->label('Status')
                            ->relationship('status', 'nome')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(function () use ($user) {
                                if (!$user || $user->isUsuario()) {
                                    return Status::where('nome', 'Solicitada')->pluck('nome', 'id');
                                }
                                return Status::pluck('nome', 'id');
                            })
                            ->default(fn() => Status::where('nome', 'Solicitada')->first()?->id),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Observações')
                    ->schema([
                        Forms\Components\Textarea::make('observacao')
                            ->label('Observação')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();

        return $table
            ->columns([
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
                Tables\Columns\TextColumn::make('solicitante.nome')
                    ->label('Solicitante')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('responsavel.nome')
                    ->label('Responsável')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status.nome')
                    ->label('Status')
                    ->badge()
                    ->color(fn($record) => $record->status->cor ?? 'gray')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_id')
                    ->label('Status')
                    ->relationship('status', 'nome')
                    ->searchable()
                    ->preload(),
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
                    ->visible(fn() => Auth::user()?->canManageSystem() ?? false),
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
