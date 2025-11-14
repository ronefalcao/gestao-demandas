<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput\Mask;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Administração';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dados pessoais')
                    ->schema([
                        Forms\Components\TextInput::make('nome')
                            ->label('Nome completo')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('telefone')
                            ->tel()
                            ->mask(fn (Mask $mask) => $mask->pattern('(00) 00000-0000'))
                            ->maxLength(20)
                            ->label('Telefone')
                            ->helperText('Opcional'),
                    ]),
                Forms\Components\Section::make('Credenciais de acesso')
                    ->schema([
                        Forms\Components\Select::make('tipo')
                            ->label('Perfil')
                            ->options([
                                'administrador' => 'Administrador',
                                'gestor' => 'Gestor',
                                'usuario' => 'Usuário',
                            ])
                            ->default('usuario')
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->revealable()
                            ->required(fn (string $context) => $context === 'create')
                            ->dehydrateStateUsing(fn (?string $state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->minLength(8)
                            ->same('password_confirmation'),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Confirmar senha')
                            ->password()
                            ->revealable()
                            ->dehydrated(false)
                            ->required(fn (string $context) => $context === 'create'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telefone')
                    ->label('Telefone')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Perfil')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'administrador' => 'success',
                        'gestor' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->label('Filtrar por perfil')
                    ->options([
                        'administrador' => 'Administrador',
                        'gestor' => 'Gestor',
                        'usuario' => 'Usuário',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
