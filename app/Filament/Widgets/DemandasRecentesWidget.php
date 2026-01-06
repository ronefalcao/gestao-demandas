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

    public static function canView(): bool
    {
        $user = Auth::user();
        // Não mostrar para planejadores e gestores
        return $user && !$user->isPlanejador() && !$user->isGestor();
    }

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

        // Excluir rascunhos de outros usuários (apenas o criador vê seus rascunhos)
        $baseQuery->excludeRascunhosFromOthers($user->id);

        // Filtrar por status baseado no tipo de usuário
        if ($user->isUsuario()) {
            // Regra 1: Tipo usuário -> mostra apenas demandas com status "Rascunho"
            $baseQuery->whereHas('status', function ($query) {
                $query->where('nome', 'Rascunho');
            });
        } else {
            // Regra 2: Outros tipos -> mostra apenas demandas com status "Solicitada"
            $baseQuery->whereHas('status', function ($query) {
                $query->where('nome', 'Solicitada');
            });
        }

        $columns = [
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
            Tables\Columns\TextColumn::make('status.nome')
                ->label('Status')
                ->badge()
                ->color(fn($record) => $record->status->cor ?? 'gray')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('prioridade')
                ->label('Prioridade')
                ->badge()
                ->formatStateUsing(fn($state) => match ($state) {
                    'baixa' => 'Baixa',
                    'media' => 'Média',
                    'alta' => 'Alta',
                    default => ucfirst($state),
                })
                ->color(fn($state) => match ($state) {
                    'baixa' => 'success',
                    'media' => 'warning',
                    'alta' => 'danger',
                    default => 'gray',
                })
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('descricao')
                ->label('Descrição')
                ->limit(60)
                ->tooltip(fn($record) => $record->descricao)
                ->searchable(),
        ];

        // Adicionar coluna solicitante apenas se não for usuário comum
        if (!$user->isUsuario()) {
            $columns[] = Tables\Columns\TextColumn::make('solicitante.nome')
                ->label('Solicitante')
                ->searchable();
        }

        return $table
            ->query($baseQuery->with(['cliente', 'projeto', 'solicitante', 'responsavel', 'status']))
            ->recordUrl(fn(Demanda $record): string => \App\Filament\Resources\DemandaResource::getUrl('view', ['record' => $record]))
            ->columns($columns)
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->actionsPosition(\Filament\Tables\Enums\ActionsPosition::BeforeColumns)
            ->defaultSort('created_at', 'desc')
            ->paginated([10]);
    }
}
