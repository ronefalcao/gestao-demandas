<?php

namespace App\Filament\Widgets;

use App\Models\Item;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class ItensPorStatusWidget extends Widget
{
    protected static string $view = 'filament.widgets.itens-por-status-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        // Desabilitar este widget
        return false;
    }

    public array $totais = [];

    public function mount(): void
    {
        $user = Auth::user();

        // Base query para itens através de features
        $baseQuery = Item::query()
            ->join('features', 'itens.feature_id', '=', 'features.id')
            ->join('projetos', 'features.projeto_id', '=', 'projetos.id')
            ->where('projetos.ativo', true);

        if (!$user->isAdmin()) {
            $projetosIds = $user->projetos()->pluck('projetos.id');
            if ($projetosIds->isEmpty()) {
                $baseQuery->whereRaw('1 = 0');
            } else {
                $baseQuery->whereIn('projetos.id', $projetosIds);
            }
        }

        $statuses = [
            ['id' => 'aberto', 'nome' => 'Aberto', 'cor' => '#f59e0b'],
            ['id' => 'fechado', 'nome' => 'Fechado', 'cor' => '#10b981'],
        ];

        foreach ($statuses as $status) {
            $countQuery = clone $baseQuery;
            $this->totais[] = [
                'id' => $status['id'],
                'nome' => $status['nome'],
                'cor' => $status['cor'],
                'total' => $countQuery->where('itens.status', $status['id'])->count(),
            ];
        }
    }
}

