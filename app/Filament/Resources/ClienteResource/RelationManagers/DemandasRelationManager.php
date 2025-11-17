<?php

namespace App\Filament\Resources\ClienteResource\RelationManagers;

use App\Filament\Resources\DemandaResource;
use App\Models\Demanda;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DemandasRelationManager extends RelationManager
{
    protected static string $relationship = 'demandas';

    protected static ?string $title = 'Demandas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('numero')
                    ->label('Número')
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\DatePicker::make('data')
                    ->label('Data')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                Forms\Components\Select::make('projeto_id')
                    ->label('Projeto')
                    ->relationship('projeto', 'nome')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('modulo')
                    ->label('Módulo')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('descricao')
                    ->label('Descrição')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
                Forms\Components\Select::make('status_id')
                    ->label('Status')
                    ->relationship('status', 'nome')
                    ->required()
                    ->searchable()
                    ->preload(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('numero')
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('data')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('projeto.nome')
                    ->label('Projeto')
                    ->searchable()
                    ->sortable(),
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
                Tables\Filters\SelectFilter::make('projeto_id')
                    ->label('Projeto')
                    ->relationship('projeto', 'nome')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Visualizar')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->modalHeading(fn(Demanda $record) => 'Demanda #' . $record->numero)
                    ->modalContent(function (Demanda $record) {
                        $record->load(['cliente', 'projeto', 'solicitante', 'responsavel', 'status']);
                        return view('filament.infolists.demanda-details', ['record' => $record]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar')
                    ->modalWidth('4xl'),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('data', 'desc')
            ->emptyStateHeading('Nenhuma demanda encontrada')
            ->emptyStateDescription('Este cliente ainda não possui demandas cadastradas.')
            ->emptyStateIcon('heroicon-o-document-text');
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        $user = Auth::user();
        return $user && ($user->canManageSystem() || $user->isGestor());
    }
}
