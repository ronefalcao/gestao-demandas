<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjetoResource\Pages;
use App\Filament\Resources\ProjetoResource\RelationManagers;
use App\Models\Projeto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ProjetoResource extends Resource
{
    protected static ?string $model = Projeto::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?int $navigationSort = 3;

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
                Forms\Components\Textarea::make('descricao')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('ativo')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->searchable(),
                Tables\Columns\IconColumn::make('ativo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->visible(fn() => Auth::user()?->isGestor() ?? false),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjetos::route('/'),
            'create' => Pages\CreateProjeto::route('/create'),
            'edit' => Pages\EditProjeto::route('/{record}/edit'),
        ];
    }
}
