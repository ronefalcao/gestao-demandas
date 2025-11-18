<?php

namespace App\Filament\Widgets;

use App\Models\Item;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class ItensRecentesWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        // Desabilitar este widget
        return false;
    }

    public function table(Table $table): Table
    {
        $user = Auth::user();

        // Base query para itens através de features
        $baseQuery = Item::query()
            ->with(['feature.projeto', 'sprint'])
            ->join('features', 'itens.feature_id', '=', 'features.id')
            ->join('projetos', 'features.projeto_id', '=', 'projetos.id')
            ->where('projetos.ativo', true)
            ->select('itens.*');

        if (!$user->isAdmin()) {
            $projetosIds = $user->projetos()->pluck('projetos.id');
            if ($projetosIds->isEmpty()) {
                $baseQuery->whereRaw('1 = 0');
            } else {
                $baseQuery->whereIn('projetos.id', $projetosIds);
            }
        }

        return $table
            ->query($baseQuery)
            ->recordUrl(fn(Item $record): string => \App\Filament\Resources\FeatureResource::getUrl('view', ['record' => $record->feature_id]))
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
                Tables\Columns\TextColumn::make('feature.numero')
                    ->label('Feature')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state ?? '-'),
                Tables\Columns\TextColumn::make('feature.projeto.nome')
                    ->label('Projeto')
                    ->searchable()
                    ->sortable()
                    ->default('-'),
                Tables\Columns\TextColumn::make('sprint.numero')
                    ->label('Sprint')
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
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10]);
    }
}

