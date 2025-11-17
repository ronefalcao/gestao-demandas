<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClienteResource\Pages;
use App\Filament\Resources\ClienteResource\RelationManagers;
use App\Models\Cliente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && ($user->canManageSystem() || $user->isGestor());
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user && $user->canManageSystem();
    }

    public static function canView($record): bool
    {
        $user = Auth::user();
        return $user && ($user->canManageSystem() || $user->isGestor());
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();
        return $user && $user->canManageSystem();
    }

    public static function canDelete($record): bool
    {
        $user = Auth::user();
        return $user && $user->canManageSystem();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('demandas_count')
                    ->label('Demandas Não Concluídas')
                    ->badge()
                    ->color('warning')
                    ->default(0)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->visible(fn() => (Auth::user()?->canManageSystem() ?? false) || (Auth::user()?->isGestor() ?? false)),
                Tables\Actions\EditAction::make()
                    ->visible(fn() => Auth::user()?->canManageSystem() ?? false),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => Auth::user()?->canManageSystem() ?? false),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DemandasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientes::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'view' => Pages\ViewCliente::route('/{record}'),
            'edit' => Pages\EditCliente::route('/{record}/edit'),
        ];
    }
}
