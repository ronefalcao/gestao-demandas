<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModuloResource\Pages;
use App\Filament\Resources\ModuloResource\RelationManagers;
use App\Models\Modulo;
use App\Models\Projeto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ModuloResource extends Resource
{
    protected static ?string $model = Modulo::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Módulos';

    protected static ?int $navigationSort = 4;

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        
        // Verificar se é admin, gestor ou planejador
        return $user->canManageSystem() || $user->isGestor() || $user->isPlanejador();
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
                Forms\Components\Section::make('Informações do Módulo')
                    ->schema([
                        Forms\Components\Select::make('projeto_id')
                            ->label('Projeto')
                            ->relationship('projeto', 'nome')
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
                                // Planejador só vê projetos aos quais tem acesso
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
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('projeto.nome')
                    ->label('Projeto')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('features_count')
                    ->label('Features')
                    ->counts('features')
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
            ])
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModulos::route('/'),
            'create' => Pages\CreateModulo::route('/create'),
            'edit' => Pages\EditModulo::route('/{record}/edit'),
        ];
    }
}
