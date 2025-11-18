<?php

namespace App\Filament\Resources\FeatureResource\RelationManagers;

use App\Models\Sprint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ItensRelationManager extends RelationManager
{
    protected static string $relationship = 'itens';

    protected static ?string $title = 'Itens';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações Básicas')
                    ->schema([
                        Forms\Components\TextInput::make('numero')
                            ->label('Número')
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\TextInput::make('titulo')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('descricao')
                            ->label('Descrição')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Detalhes')
                    ->schema([
                        Forms\Components\TextInput::make('figma')
                            ->label('Figma')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('us')
                            ->label('US (User Story)')
                            ->maxLength(255),
                        Forms\Components\Select::make('sprint_id')
                            ->label('Sprint')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->options(fn() => Sprint::orderBy('numero', 'desc')->pluck('numero', 'id')),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'aberto' => 'Aberto',
                                'fechado' => 'Fechado',
                            ])
                            ->default('aberto')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('titulo')
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('Número')
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
                Tables\Columns\TextColumn::make('figma')
                    ->label('Figma')
                    ->url(fn($record) => $record->figma)
                    ->openUrlInNewTab()
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('us')
                    ->label('US')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sprint.numero')
                    ->label('Sprint')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->default('-'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($record) => $record->status === 'fechado' ? 'success' : 'warning')
                    ->formatStateUsing(fn($state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'aberto' => 'Aberto',
                        'fechado' => 'Fechado',
                    ]),
                Tables\Filters\SelectFilter::make('sprint_id')
                    ->label('Sprint')
                    ->relationship('sprint', 'numero')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Novo Item')
                    ->icon('heroicon-o-plus')
                    ->authorize(fn() => $this->canCreate())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['feature_id'] = $this->getOwnerRecord()->id;
                        // Converter sprint_id vazio para null
                        if (empty($data['sprint_id'])) {
                            $data['sprint_id'] = null;
                        }
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->authorize(fn(Model $record) => $this->canEdit($record))
                    ->mutateFormDataUsing(function (array $data): array {
                        // Converter sprint_id vazio para null
                        if (empty($data['sprint_id'])) {
                            $data['sprint_id'] = null;
                        }
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->authorize(fn(Model $record) => $this->canDelete($record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                // Ordenar por número considerando o formato decimal (1.1, 1.2, etc.)
                return $query->orderByRaw('CAST(SPLIT_PART(numero, \'.\', 1) AS INTEGER) ASC, CAST(SPLIT_PART(numero, \'.\', 2) AS INTEGER) ASC');
            });
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        $user = Auth::user();
        return $user && ($user->canManageSystem() || $user->isPlanejador());
    }

    public function canCreate(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        return $user->canManageSystem() || $user->isPlanejador();
    }

    public function canEdit(Model $record): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        return $user->canManageSystem() || $user->isPlanejador();
    }

    public function canDelete(Model $record): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        return $user->canManageSystem() || $user->isPlanejador();
    }
}