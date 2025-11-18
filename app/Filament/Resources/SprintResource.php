<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SprintResource\Pages;
use App\Filament\Resources\SprintResource\RelationManagers;
use App\Models\Sprint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class SprintResource extends Resource
{
    protected static ?string $model = Sprint::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Sprints';

    protected static ?int $navigationSort = 6;

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && ($user->canManageSystem() || $user->isPlanejador());
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user && ($user->canManageSystem() || $user->isPlanejador());
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();
        return $user && ($user->canManageSystem() || $user->isPlanejador());
    }

    public static function canDelete($record): bool
    {
        $user = Auth::user();
        return $user && ($user->canManageSystem() || $user->isPlanejador());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações da Sprint')
                    ->schema([
                        Forms\Components\TextInput::make('numero')
                            ->label('Número')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\DatePicker::make('inicio')
                            ->label('Início')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('termino')
                            ->label('Término')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->after('inicio'),
                    ])
                    ->columns(3),
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
                Tables\Columns\TextColumn::make('inicio')
                    ->label('Início')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('termino')
                    ->label('Término')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('itens_count')
                    ->label('Itens')
                    ->counts('itens')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('numero', 'desc');
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
            'index' => Pages\ListSprints::route('/'),
            'create' => Pages\CreateSprint::route('/create'),
            'edit' => Pages\EditSprint::route('/{record}/edit'),
        ];
    }
}
