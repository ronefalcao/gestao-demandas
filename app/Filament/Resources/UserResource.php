<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Models\Projeto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Usuários';

    protected static ?int $navigationSort = 4;

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
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('telefone')
                    ->tel()
                    ->maxLength(20),
                Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->dehydrated(fn($state) => filled($state))
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->maxLength(255),
                Forms\Components\Select::make('tipo')
                    ->required()
                    ->options([
                        'administrador' => 'Administrador',
                        'gestor' => 'Gestor',
                        'analista' => 'Analista',
                        'planejador' => 'Planejador',
                        'usuario' => 'Usuário',
                    ])
                    ->default('usuario'),
                Forms\Components\Select::make('projetos')
                    ->label('Projetos com Acesso')
                    ->relationship('projetos', 'nome')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->visible(fn($get) => $get('tipo') !== 'administrador'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telefone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->searchable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'administrador' => 'danger',
                        'gestor' => 'warning',
                        'analista' => 'success',
                        'planejador' => 'purple',
                        'usuario' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('projetos.nome')
                    ->label('Projetos')
                    ->badge()
                    ->separator(',')
                    ->toggleable(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
