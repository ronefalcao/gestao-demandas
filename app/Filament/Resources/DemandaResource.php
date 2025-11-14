<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DemandaResource\Pages;
use App\Models\Demanda;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DemandaResource extends Resource
{
    protected static ?string $model = Demanda::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Operacional';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identificação')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('numero')
                            ->label('Número')
                            ->disabled()
                            ->dehydrated(false)
                            ->hint('Gerado automaticamente'),
                        Forms\Components\DatePicker::make('data')
                            ->label('Data')
                            ->required()
                            ->default(now()),
                        Forms\Components\Select::make('status_id')
                            ->label('Status')
                            ->relationship('status', 'nome')
                            ->searchable()
                            ->required()
                            ->preload(),
                    ]),
                Forms\Components\Section::make('Relacionamentos')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('cliente_id')
                            ->label('Cliente')
                            ->relationship('cliente', 'nome')
                            ->searchable()
                            ->required()
                            ->preload(),
                        Forms\Components\Select::make('projeto_id')
                            ->label('Projeto')
                            ->relationship('projeto', 'nome')
                            ->preload()
                            ->searchable()
                            ->helperText('Opcional'),
                        Forms\Components\Select::make('solicitante_id')
                            ->label('Solicitante')
                            ->relationship('solicitante', 'nome')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('responsavel_id')
                            ->label('Responsável')
                            ->relationship('responsavel', 'nome')
                            ->searchable()
                            ->preload(),
                    ]),
                Forms\Components\Section::make('Detalhes')
                    ->schema([
                        Forms\Components\TextInput::make('modulo')
                            ->label('Módulo/Área')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('descricao')
                            ->label('Descrição')
                            ->rows(5)
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('observacao')
                            ->label('Observações internas')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('Nº')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('data')
                    ->label('Data')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cliente.nome')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('projeto.nome')
                    ->label('Projeto')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status.nome')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => fn (Demanda $record) => str_contains(strtolower($record->status?->nome ?? ''), 'concl'),
                        'warning' => fn (Demanda $record) => str_contains(strtolower($record->status?->nome ?? ''), 'aguard'),
                        'danger' => fn (Demanda $record) => str_contains(strtolower($record->status?->nome ?? ''), 'pend'),
                        'info' => fn () => true,
                    ]),
                Tables\Columns\TextColumn::make('solicitante.nome')
                    ->label('Solicitante')
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('responsavel.nome')
                    ->label('Responsável')
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('modulo')
                    ->label('Módulo')
                    ->limit(20)
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_id')
                    ->label('Status')
                    ->relationship('status', 'nome'),
                Tables\Filters\SelectFilter::make('cliente_id')
                    ->label('Cliente')
                    ->relationship('cliente', 'nome'),
                Tables\Filters\Filter::make('periodo')
                    ->form([
                        Forms\Components\DatePicker::make('data_inicial')
                            ->label('De'),
                        Forms\Components\DatePicker::make('data_final')
                            ->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['data_inicial'] ?? null, fn (Builder $q, $date) => $q->whereDate('data', '>=', $date))
                            ->when($data['data_final'] ?? null, fn (Builder $q, $date) => $q->whereDate('data', '<=', $date));
                    }),
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
            'index' => Pages\ListDemandas::route('/'),
            'create' => Pages\CreateDemanda::route('/create'),
            'edit' => Pages\EditDemanda::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->latest('data');
    }
}
