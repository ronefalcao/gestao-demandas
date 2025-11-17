<?php

namespace App\Filament\Widgets;

use App\Models\Demanda;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class DemandasRecentesWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        $user = Auth::user();

        // Base query para demandas
        $baseQuery = Demanda::query();

        if (!$user->isAdmin()) {
            $projetosIds = $user->projetos()->pluck('projetos.id');
            if ($projetosIds->isEmpty()) {
                $baseQuery->whereRaw('1 = 0');
            } else {
                $baseQuery->whereIn('projeto_id', $projetosIds);
                if ($user->isUsuario()) {
                    $baseQuery->where('solicitante_id', $user->id);
                }
            }
        }

        return $table
            ->query($baseQuery->with(['cliente', 'projeto', 'solicitante', 'responsavel', 'status']))
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('data')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cliente.nome')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('projeto.nome')
                    ->label('Projeto')
                    ->searchable()
                    ->sortable()
                    ->default('-'),
                Tables\Columns\TextColumn::make('modulo')
                    ->label('Módulo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->limit(60)
                    ->searchable(),
                Tables\Columns\TextColumn::make('status.nome')
                    ->label('Status')
                    ->badge()
                    ->color(fn($record) => $record->status->cor ?? 'gray'),
                Tables\Columns\TextColumn::make('solicitante.nome')
                    ->label('Solicitante')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Detalhes')
                    ->url(fn(Demanda $record): string => \App\Filament\Resources\DemandaResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10]);
    }
}