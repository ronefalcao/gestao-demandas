<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeatureResource\Pages;
use App\Filament\Resources\FeatureResource\RelationManagers;
use App\Models\Feature;
use App\Models\Modulo;
use App\Models\Projeto;
use App\Models\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class FeatureResource extends Resource
{
    protected static ?string $model = Feature::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'Features';

    protected static ?int $navigationSort = 9;

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && ($user->canManageSystem() || $user->isPlanejador() || $user->isGestor());
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user && ($user->canManageSystem() || $user->isPlanejador() || $user->isGestor());
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();
        return $user && ($user->canManageSystem() || $user->isPlanejador() || $user->isGestor());
    }

    public static function canDelete($record): bool
    {
        $user = Auth::user();
        return $user && ($user->canManageSystem() || $user->isPlanejador() || $user->isGestor());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações Básicas')
                    ->schema([
                        Forms\Components\TextInput::make('numero')
                            ->label('Número')
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Select::make('projeto_id')
                            ->label('Projeto')
                            ->relationship('projeto', 'nome')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->options(function () {
                                $user = Auth::user();
                                if (!$user) {
                                    return [];
                                }
                                if ($user->isAdmin()) {
                                    return Projeto::where('ativo', true)->pluck('nome', 'id');
                                }
                                // Planejador só vê projetos aos quais tem acesso
                                return $user->projetos()
                                    ->where('projetos.ativo', true)
                                    ->select('projetos.id', 'projetos.nome')
                                    ->pluck('projetos.nome', 'projetos.id');
                            }),
                        Forms\Components\Select::make('modulo_id')
                            ->label('Módulo')
                            ->relationship('modulo', 'nome')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(function (Forms\Get $get) {
                                $projetoId = $get('projeto_id');
                                if (!$projetoId) {
                                    return [];
                                }
                                return Modulo::where('projeto_id', $projetoId)
                                    ->orderBy('nome')
                                    ->pluck('nome', 'id');
                            })
                            ->createOptionForm([
                                Forms\Components\Select::make('projeto_id')
                                    ->label('Projeto')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->options(function () {
                                        $user = Auth::user();
                                        if (!$user) {
                                            return [];
                                        }
                                        if ($user->isAdmin()) {
                                            return Projeto::where('ativo', true)->pluck('nome', 'id');
                                        }
                                        return $user->projetos()
                                            ->where('projetos.ativo', true)
                                            ->select('projetos.id', 'projetos.nome')
                                            ->pluck('projetos.nome', 'projetos.id');
                                    }),
                                Forms\Components\TextInput::make('nome')
                                    ->label('Nome')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('descricao')
                                    ->label('Descrição')
                                    ->rows(3),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                return Modulo::create($data)->id;
                            }),
                        Forms\Components\TextInput::make('titulo')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(3),
                Forms\Components\Section::make('Detalhes')
                    ->schema([
                        Forms\Components\Textarea::make('descricao')
                            ->label('Descrição')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('status_id')
                            ->label('Status')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(function () {
                                // Mostrar apenas status com IDs 10, 4, 5, 6 nessa ordem
                                $statusIds = [10, 4, 5, 6];
                                $statuses = Status::whereIn('id', $statusIds)->get();

                                // Ordenar na ordem especificada
                                $orderedStatuses = collect($statusIds)->map(function ($id) use ($statuses) {
                                    return $statuses->firstWhere('id', $id);
                                })->filter();

                                return $orderedStatuses->pluck('nome', 'id');
                            }),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('projeto.nome')
                    ->label('Projeto')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('modulo.nome')
                    ->label('Módulo')
                    ->formatStateUsing(fn($record) => $record->modulo ? (is_object($record->modulo) ? $record->modulo->nome : $record->modulo) : '-')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('titulo')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->limit(60)
                    ->searchable()
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
                Tables\Filters\SelectFilter::make('projeto_id')
                    ->label('Projeto')
                    ->relationship('projeto', 'nome', function ($query) {
                        $user = Auth::user();
                        if (!$user || $user->isAdmin()) {
                            return $query;
                        }
                        // Planejador só vê projetos aos quais tem acesso
                        $projetosIds = $user->projetos()->pluck('projetos.id');
                        return $query->whereIn('projetos.id', $projetosIds);
                    })
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status_id')
                    ->label('Status')
                    ->relationship('status', 'nome', fn($query) => $query->orderBy('ordem'))
                    ->searchable()
                    ->preload(),
            ])
            ->recordUrl(fn(Feature $record): string => static::getUrl('view', ['record' => $record]))
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItensRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeatures::route('/'),
            'create' => Pages\CreateFeature::route('/create'),
            'view' => Pages\ViewFeature::route('/{record}'),
            'edit' => Pages\EditFeature::route('/{record}/edit'),
        ];
    }
}
